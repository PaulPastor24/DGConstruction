'use client'

import { makeStyles, mergeClasses, tokens, Title1, Title3, Body1, Body2, Badge } from '@fluentui/react-components'
import { CheckmarkCircle24Regular, Warning24Regular, Clock24Regular } from '@fluentui/react-icons'

const useStyles = makeStyles({
  root: {
    backgroundColor: '#FFFFFF',
    borderRadius: '20px',
    padding: '32px',
    boxShadow: '0 2px 8px rgba(0, 0, 0, 0.08)',
    border: `1px solid rgba(0, 0, 0, 0.06)`,
  },
  header: {
    marginBottom: '40px',
    paddingBottom: '24px',
    borderBottom: '1px solid rgba(0, 0, 0, 0.08)',
  },
  greeting: {
    fontSize: '16px',
    fontWeight: 500,
    color: '#666666',
    marginBottom: '12px',
  },
  title: {
    marginBottom: '8px',
    color: '#2F6B3C',
    fontSize: '34px',
    fontWeight: 700,
    letterSpacing: '-0.5px',
  },
  subtitle: {
    color: '#999999',
    marginBottom: 0,
    fontSize: '15px',
    fontWeight: 400,
    lineHeight: '1.5',
  },
  blocks: {
    display: 'grid',
    gridTemplateColumns: 'repeat(4, 1fr)',
    gap: '24px',
    marginBottom: '40px',
    '@media (max-width: 1200px)': {
      gridTemplateColumns: 'repeat(2, 1fr)',
    },
    '@media (max-width: 768px)': {
      gridTemplateColumns: '1fr',
    },
  },
  block: {
    backgroundColor: '#FFFFFF',
    borderRadius: '16px',
    padding: '24px',
    border: '1px solid rgba(0, 0, 0, 0.08)',
    borderLeft: '4px solid #66BB6A',
    boxShadow: '0 1px 3px rgba(0, 0, 0, 0.06)',
    transition: 'all 0.3s ease',
    '&:hover': {
      boxShadow: '0 4px 12px rgba(0, 0, 0, 0.1)',
      transform: 'translateY(-2px)',
    },
  },
  blockTitle: {
    fontSize: '13px',
    fontWeight: 700,
    color: '#333333',
    marginBottom: '12px',
    textTransform: 'none',
    letterSpacing: '0px',
  },
  blockValue: {
    fontSize: '32px',
    fontWeight: 700,
    color: '#2F6B3C',
    marginBottom: '6px',
    lineHeight: '1.1',
  },
  blockMetric: {
    fontSize: '14px',
    color: '#666666',
    marginBottom: '16px',
    fontWeight: 400,
  },
  badge: {
    display: 'inline-block',
  },
  buttons: {
    display: 'flex',
    gap: '16px',
    justifyContent: 'center',
    marginTop: '16px',
    '@media (max-width: 768px)': {
      flexWrap: 'wrap',
      gap: '12px',
    },
  },
  buttonBase: {
    paddingTop: '13px',
    paddingBottom: '13px',
    paddingLeft: '32px',
    paddingRight: '32px',
    borderRadius: '10px',
    border: 'none',
    cursor: 'pointer',
    fontSize: '15px',
    fontWeight: 700,
    transition: 'all 0.3s ease',
    height: '48px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    boxShadow: '0 2px 4px rgba(0, 0, 0, 0.08)',
  },
  buttonPrimary: {
    backgroundColor: '#2F6B3C',
    color: '#FFFFFF',
    '&:hover': {
      backgroundColor: '#1f4620',
      boxShadow: '0 4px 12px rgba(47, 107, 60, 0.3)',
      transform: 'translateY(-2px)',
    },
  },
  buttonSecondary: {
    backgroundColor: '#F0F0E8',
    color: '#2F6B3C',
    border: 'none',
    fontWeight: 700,
    '&:hover': {
      backgroundColor: '#E8E8DE',
      boxShadow: '0 4px 12px rgba(0, 0, 0, 0.12)',
      transform: 'translateY(-2px)',
    },
  },
  buttonOutline: {
    backgroundColor: 'transparent',
    color: '#2F6B3C',
    border: '1.5px solid #2F6B3C',
    boxShadow: 'none',
    '&:hover': {
      backgroundColor: 'rgba(47, 107, 60, 0.08)',
      boxShadow: '0 4px 12px rgba(0, 0, 0, 0.1)',
      transform: 'translateY(-2px)',
    },
  },
})

export function SiteCommandCenter() {
  const styles = useStyles()

  return (
    <div className={styles.root}>
      <div className={styles.header}>
        <div className={styles.greeting}>Good Morning, John Mitchell</div>
        <Title1 className={styles.title}>Today&apos;s Site Priorities</Title1>
        <Body1 className={styles.subtitle}>Operational summary for your assigned construction project.</Body1>
      </div>

      <div className={styles.blocks}>
        <div className={styles.block}>
          <div className={styles.blockTitle}>Workforce Readiness</div>
          <div className={styles.blockValue}>48 / 51</div>
          <div className={styles.blockMetric}>Workers Present</div>
          <Badge className={styles.badge} appearance="tint" color="warning">
            Pending
          </Badge>
          <div style={{ marginTop: '8px', fontSize: '12px', color: '#999999' }}>3 workers still not recorded</div>
        </div>

        <div className={styles.block}>
          <div className={styles.blockTitle}>Current Site Phase</div>
          <div className={styles.blockValue}>Roofing</div>
          <div className={styles.blockMetric}>65% Complete</div>
          <div style={{ marginTop: '8px', fontSize: '12px', color: '#666666' }}>Started June 5 • Expected July 10</div>
        </div>

        <div className={styles.block}>
          <div className={styles.blockTitle}>Today&apos;s Site Tasks</div>
          <div style={{ marginBottom: '12px' }}>
            <div style={{ fontSize: '13px', color: '#333333', marginBottom: '6px', display: 'flex', alignItems: 'center', gap: '6px' }}>
              <span style={{ fontSize: '14px' }}>☐</span> Record Attendance
            </div>
            <div style={{ fontSize: '13px', color: '#333333', marginBottom: '6px', display: 'flex', alignItems: 'center', gap: '6px' }}>
              <span style={{ fontSize: '14px' }}>☐</span> Submit Daily Report
            </div>
            <div style={{ fontSize: '13px', color: '#333333', display: 'flex', alignItems: 'center', gap: '6px' }}>
              <span style={{ fontSize: '14px' }}>☐</span> Verify Milestone Progress
            </div>
          </div>
        </div>

        <div className={styles.block} style={{ backgroundColor: 'rgba(255, 238, 88, 0.08)', borderTop: '3px solid #FFEE58' }}>
          <div className={styles.blockTitle} style={{ color: '#996600' }}>Upcoming Deadline</div>
          <div className={styles.blockValue} style={{ fontSize: '24px', color: '#996600' }}>Tomorrow</div>
          <div className={styles.blockMetric}>Roof Inspection</div>
          <Badge className={styles.badge} appearance="tint" color="warning">
            High Priority
          </Badge>
        </div>
      </div>

      <div className={styles.buttons}>
        <button className={mergeClasses(styles.buttonBase, styles.buttonPrimary)}>Record Attendance</button>
        <button className={mergeClasses(styles.buttonBase, styles.buttonSecondary)}>Submit Report</button>
        <button className={mergeClasses(styles.buttonBase, styles.buttonOutline)}>Project Timeline</button>
      </div>
    </div>
  )
}
