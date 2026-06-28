'use client'

import { makeStyles } from '@fluentui/react-components'

const useStyles = makeStyles({
  root: {
    backgroundColor: '#FFFFFF',
    borderRadius: '16px',
    padding: '32px',
    boxShadow: '0 1px 3px rgba(0, 0, 0, 0.06)',
    border: '1px solid rgba(0, 0, 0, 0.08)',
  },
  header: {
    display: 'grid',
    gridTemplateColumns: '1fr auto',
    alignItems: 'start',
    gap: '40px',
    marginBottom: '32px',
    '@media (max-width: 768px)': {
      gridTemplateColumns: '1fr',
    },
  },
  headerContent: {
    display: 'flex',
    flexDirection: 'column',
    gap: '8px',
  },
  title: {
    fontSize: '20px',
    fontWeight: 700,
    color: '#2F6B3C',
  },
  subtitle: {
    fontSize: '14px',
    color: '#999999',
    fontWeight: 400,
  },
  progressCircle: {
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
    justifyContent: 'center',
    width: '140px',
    height: '140px',
    borderRadius: '50%',
    backgroundColor: '#F5F5F0',
    border: '4px solid #EEEEEA',
    position: 'relative',
  },
  progressValue: {
    fontSize: '36px',
    fontWeight: 700,
    color: '#2F6B3C',
  },
  progressLabel: {
    fontSize: '12px',
    color: '#999999',
    fontWeight: 600,
    marginTop: '4px',
  },
  grid: {
    display: 'grid',
    gridTemplateColumns: 'repeat(2, 1fr)',
    gap: '32px',
    marginBottom: '32px',
    '@media (max-width: 768px)': {
      gridTemplateColumns: '1fr',
    },
  },
  item: {
    display: 'flex',
    flexDirection: 'column',
    gap: '8px',
  },
  label: {
    fontSize: '13px',
    color: '#999999',
    fontWeight: 700,
    textTransform: 'uppercase',
    letterSpacing: '0.5px',
  },
  value: {
    fontSize: '16px',
    fontWeight: 700,
    color: '#333333',
  },
  buttons: {
    display: 'flex',
    gap: '16px',
    justifyContent: 'center',
    marginTop: '32px',
    '@media (max-width: 768px)': {
      flexDirection: 'column',
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
    minWidth: '200px',
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
    '&:hover': {
      backgroundColor: '#E8E8DE',
      boxShadow: '0 4px 12px rgba(0, 0, 0, 0.12)',
      transform: 'translateY(-2px)',
    },
  },
})

export function CurrentPhaseDetails() {
  const styles = useStyles()

  return (
    <div className={styles.root}>
      <div className={styles.header}>
        <div className={styles.headerContent}>
          <div className={styles.title}>Current Site Phase</div>
          <div className={styles.subtitle}>Active phase details and timeline</div>
        </div>
        <div className={styles.progressCircle}>
          <div className={styles.progressValue}>65%</div>
          <div className={styles.progressLabel}>Complete</div>
        </div>
      </div>

      <div className={styles.grid}>
        <div className={styles.item}>
          <div className={styles.label}>Phase Name</div>
          <div className={styles.value}>Roofing</div>
        </div>
        <div className={styles.item}>
          <div className={styles.label}>Current Status</div>
          <div className={styles.value} style={{ color: '#2F6B3C', fontSize: '14px', fontWeight: 700 }}>In Progress</div>
        </div>
        <div className={styles.item}>
          <div className={styles.label}>Planned Start</div>
          <div className={styles.value}>Jun 21, 2024</div>
        </div>
        <div className={styles.item}>
          <div className={styles.label}>Actual Start</div>
          <div className={styles.value}>Jun 21, 2024</div>
        </div>
        <div className={styles.item}>
          <div className={styles.label}>Expected Finish</div>
          <div className={styles.value}>Jul 10, 2024</div>
        </div>
        <div className={styles.item}>
          <div className={styles.label}>Days Remaining</div>
          <div className={styles.value}>14 Days</div>
        </div>
        <div className={styles.item}>
          <div className={styles.label}>Assigned Workers</div>
          <div className={styles.value}>24 Workers</div>
        </div>
        <div className={styles.item}>
          <div className={styles.label}>Delay Risk</div>
          <div className={styles.value} style={{ color: '#2F6B3C' }}>On Schedule</div>
        </div>
      </div>

      <div className={styles.buttons}>
        <button className={`${styles.buttonBase} ${styles.buttonPrimary}`}>Update Phase Progress</button>
        <button className={`${styles.buttonBase} ${styles.buttonSecondary}`}>View Phase Details</button>
      </div>
    </div>
  )
}
